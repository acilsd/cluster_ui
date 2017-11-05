/* @flow */
import {
  START_AUTH,
  AUTH_SUCCESS,
  AUTH_FAIL,
  LOGOUT,
} from './constants';

import type { Store, Action } from './types';

const initialState: Store = {
  username: '',
  token: '',
  isLoggedIn: false,
  error: { message: '', status: '' },
};

export default function user(state: Store = initialState, action: Action): Store {
  switch (action.type) {
  case START_AUTH:
    return { ...state };
  case AUTH_SUCCESS:
    return { ...state, username: action.data.username, token: action.data.token, };
  case AUTH_FAIL:
    return { ...state, username: '', token: '', isLoggedIn: false, };
  case LOGOUT:
    return { ...state, username: '', token: '', isLoggedIn: false, };
  default:
    return state;
  }
}
