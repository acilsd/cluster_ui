/* @flow */
import type { ThunkAction, Dispatch, ActionType } from './types';

import {
  START_AUTH,
  AUTH_SUCCESS,
  AUTH_FAIL,
} from './constants';

const TEST_USER = 'testuser';
const TEST_PASS = 'testpassword';
const testEncodedAuthToken = '';

const API_PREFIX = process.env.NODE_ENV === 'development' ? 'http://localhost:1337/' : 'http://';

export const auth = (auth_data: ActionType): ThunkAction => {
  return async (dispatch: Dispatch): Promise<*> => {
    try {
      console.log('STARTING AUTH');
      dispatch({ type: START_AUTH, data: auth_data });
    } catch(e) {
      console.error(e);
      dispatch({ type: AUTH_FAIL, data: auth_data });
    } finally {
      console.log('SUCCESS!');
      dispatch({ type: AUTH_SUCCESS, data: auth_data });
    }
  };
};
