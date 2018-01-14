import axios from 'axios';
import {
  CREATE_STREAM,
  EDIT_STREAM,
  DELETE_STREAM,

  SELECT_SINGLE_STREAM,
  CLEAR_STREAM_SELECTION,

  CREATE_TASK,
  EDIT_TASK,
  DELETE_TASK,
} from './constants';

export const test = () => ({ type: CREATE_TASK });
