#!/usr/bin/env Rscript
library('forecast')
# Read arguments from commandline
args <- commandArgs(trailingOnly = TRUE)

if (length(args) == 0) {
  stop('At least one argument must be supplied')
}
filename = args[1]

# Custom frequency
if (length(args) == 2) {
  freq = args[2]
} else {
  freq = 20
}

data = read.csv(filename)
ts <- ts(data, frequency=freq)
fit <- auto.arima(ts)
print(forecast(fit, 12))
