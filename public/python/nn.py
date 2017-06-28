#!/usr/bin/python

import numpy as np
import os
import sys

from keras.models import Sequential
from keras.layers import Dense
from keras.models import load_model

class NeuralNet:
    commands = [
    'fit',              # Fit a new model with the training data
    'predict',          # Predict a value based on input
    'generate',         # Generate random training data
    'info',             # Information from training data
    'verify',           # Verify the dataset using a verification set
    'list'              # List available commands
    ]
    command = 'list'    # Default command
    args = []           # Start without extra args
    debug = True        # Show extra info

    def __init__(self):
        self.get_args()
        self.execute()

    def get_args(self):
        # Check if there is more than 1 argument
        if len(sys.argv) < 2:
            return

        # Set command to second argument
        self.command = sys.argv[1]

        # Set all other arguments as self.args
        if len(sys.argv) > 2:
            self.args = sys.argv[2:]

    # Calls the function that belongs to the command
    def execute(self):
        if self.command in self.commands:
            if self.debug:
                getattr(self, "c_%s" % self.command)()
                return
            try:
                getattr(self, "c_%s" % self.command)()
            except Exception as error:
                print "Error executing command"
                return
        print "Command '%s' not found" % self.command

    # Count if there are enough arguments
    def min_args(self, args):
        if not self.args or len(self.args) < len(args):
            print "Need %s arguments (%s)" % (len(args), ', '.join(args))
            sys.exit(1)

    '''
    --- COMMANDS ---
    '''
    def c_list(self):
        print "list"

    def c_generate(self):
        rows = 1
        if len(self.args) > 0:
            rows = int(self.args[0])
        randomdata, working = self.apply_rules(np.random.rand(rows, 4))
        np.savetxt("randomdata.csv", randomdata, delimiter=",", fmt='%.2f')
        print "Working: " + str(working) + ", not working: " + str(rows - working)

    def c_fit(self):
        dataset = np.genfromtxt('training.csv', delimiter=',')

        X = dataset[:, 0:4]
        Y = dataset[:, 4]

        model = Sequential()
        model.add(Dense(12, input_dim=4, activation='relu'))
        model.add(Dense(8, activation='relu'))
        model.add(Dense(1, activation='sigmoid'))

        model.compile(loss='binary_crossentropy', optimizer='adam', metrics=['accuracy'])

        model.fit(X, Y, epochs=1, batch_size=1)
        model.save('sitemodel.hdf5')

        scores = model.evaluate(X,Y)
        print("\n%s: %.2f%%" % (model.metrics_names[1], scores[1]*100))

    def c_predict(self):
        model = load_model('sitemodel.hdf5')
        print model.predict(np.array([[75,2,2,4]]))

    def c_verify(self):
        model = load_model('sitemodel.hdf5')
        dataset = np.genfromtxt('verify.csv', delimiter=',')

        X = dataset[:, 0:4]
        actual = dataset[:, 4]

        predictedset = model.predict(X)

        tp = 0
        tn = 0
        fp = 0
        fn = 0

        it = np.nditer(predictedset, flags=['f_index'])

        while not it.finished:
            if actual[it.index] == round(it[0]):
                if round(it[0]) == 1.0:
                    tp += 1
                else:
                    tn += 1
            else:
                if round(it[0]) == 1.0:
                    fp += 1
                else:
                    fn += 1
            it.iternext()

        print "TP: " + str(tp) + " | TN: " + str(tn) + "\nFP: " + str(fp) + " | FN: " + str(fn)
        acc = float(tp+tn) / float(tp+tn+fp+fn) * 100
        print "Accuracy: " + str(acc) + "%"

    '''
    --- HELPER FUNCTIONS ---
    '''
    def apply_rules(self, data):
        rowcount = len(data)
        newdata = np.empty([rowcount, 5])
        working = 0
        for i, row in enumerate(data.tolist()):
            isWorking = 1.0
            hdd = row[0] * 100
            ram = row[1] * 100
            page = row[2] * 16
            cpu = row[3] * 100
            if (hdd >= 90):
                if(hdd >= 99):
                    isWorking = 0.0
                else:
                    isWorking = self.rule_decision(isWorking, hdd, 0.6)

            if ram >= 80:
                rate = 0.5
                if ram > 90:
                    rate = 1.0
                isWorking = self.rule_decision(isWorking, ram, rate)

            if page >= 14:
                if np.random.random < 0.5:
                    page = -1
                isWorking = 0.0

            if cpu > 80:
                rate = 0.4
                if (cpu > 90):
                    rate = 1
                isWorking = self.rule_decision(isWorking, cpu, rate)
            newdata[i] = [hdd, ram, page, cpu, isWorking]
            if isWorking:
                working = working + 1
        return newdata, working

    def rule_decision(self, isWorking, value, rate):
        if isWorking == 0:
            return 0.0
        probability = value / 100 * rate
        if np.random.random > probability:
            return 0.0
        return 1.0

# Call the class when the file loads
if __name__ == '__main__':
    NeuralNet()
