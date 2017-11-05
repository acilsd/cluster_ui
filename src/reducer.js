/* @flow */

import { combineReducers } from 'redux';
import { reducer as formReducer } from 'redux-form';
import user from 'Auth/redux/reducer';
import tasks from 'Trixie/redux/reducer';

const reducers = {
  user,
  tasks,
  form: formReducer,
};

export type Reducers = typeof reducers;

export default combineReducers(reducers);
