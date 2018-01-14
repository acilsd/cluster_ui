/* @flow */
import type { ThunkAction, Dispatch, ActionType } from './types';

import {
  START_AUTH,
  AUTH_SUCCESS,
  AUTH_FAIL,
  RESTORE_SESSION,
  END_SESSION,
} from './constants';

const TEST_USER = 'testuser';
const TEST_PASS = 'testpassword';
const testEncodedAuthToken = '';

const API_PREFIX = process.env.NODE_ENV === 'development' ? 'http://localhost:1337/' : 'http://';

export const auth = (auth_data: ActionType): ThunkAction => {
  return async (dispatch: Dispatch): Promise<*> => {
    try {
      await dispatch({ type: START_AUTH, data: auth_data });
      console.info('STARTING AUTH');
      await dispatch({ type: AUTH_SUCCESS, data: auth_data });
      console.info('SUCCESS!');
    } catch(e) {
      dispatch({ type: AUTH_FAIL, data: auth_data });
      console.error(e);
    }
  };
};

export const restore_session = (data: { token: string, username: string }): ThunkAction => {
  return async (dispatch: any): Promise<*> => {
    try {
      await dispatch({ type: RESTORE_SESSION, data });
      console.info('SESSION RESTORED!');
    } catch (e) {
      console.error(e);
    }
  };
};

export const logout = (): ThunkAction => {
  return async (dispatch: any): Promise<*> => {
    try {
      await dispatch({ type: END_SESSION });
    } catch (e) {
      console.error(e);
    }
  };
};
