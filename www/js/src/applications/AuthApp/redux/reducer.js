/* @flow */
import {
  START_AUTH,
  AUTH_SUCCESS,
  AUTH_FAIL,
  END_SESSION,
  RESTORE_SESSION,
} from './constants';

import type { Store, Action } from './types';

const initialState: Store = {
  username: '',
  token: '',
  isLoggedIn: false,
};

export default function user(state: Store = initialState, action: Action): Store {
  switch (action.type) {
  case START_AUTH:
    return { ...state };
  case AUTH_SUCCESS:
    return { ...state, username: action.data.username, token: 'REMOVE_THIS', isLoggedIn: true, };
  case AUTH_FAIL:
    return { ...state, username: '', token: '', isLoggedIn: false, };
  case END_SESSION:
    return { ...state, username: '', token: '', isLoggedIn: false, };
  case RESTORE_SESSION:
    return { ...state, username: action.data.username, token: action.data.token, isLoggedIn: true, };
  default:
    return state;
  }
}
