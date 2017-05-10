/*
  Create a global event bus that is used to send ($emit) and
  receive ($on) data between different components.
*/

import Vue from 'vue'
export const EventBus = new Vue();
