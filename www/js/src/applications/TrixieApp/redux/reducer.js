import {
  CREATE_STREAM,
  EDIT_STREAM,
  DELETE_STREAM,
  SELECT_SINGLE_STREAM,
  CLEAR_STREAM_SELECTION,

  CREATE_TASK,
  EDIT_TASK,
  DELETE_TASK,

} from 'constants';

const initialState = {
  streamlist: [
    {
      id: 1,
      type: 'stream_unit',
      stream_type: 'core_global',
      stream_name: 'UI',
      permissions: ['system'],
      created_by: 'root_system',
      created_date: '01.01.1010',
      status: 'active',
      dead_line: null,
      priority: 'major',
      shortcut: 'Improve UI',
      description: 'Sooper dooper description',
    },
  ],
  selectedStream: null,
  tasklist: [
    {
      id: 1,
      stream_name: 'UI',
      name: 'Stream/Tasks filters',
      branch_tag: 'feature_1488_fix_shit',
      created_by: 'root_system',
      created_date: '14.88.1488',
      deadline_date: '14.88.1488',
      status: 'just_created',
      shortcut: 'fix some shit asap',
      description: 'Description is missing, hey, Description is missing, hey, Description is missing, hey, Description is missing, hey, Description is missing, hey',
      permissions: ['all_users_global'],
      participants: [''],
      is_completed: false,
    },
    {
      id: 2,
      stream_name: 'UI',
      name: 'Stream/Tasks filters',
      branch_tag: 'feature_1488_fix_shit',
      created_by: 'root_system',
      created_date: '14.88.1488',
      deadline_date: '14.88.1488',
      status: 'just_created',
      shortcut: 'fix some shit asap',
      description: 'Description is missing, hey, Description is missing, hey, Description is missing, hey, Description is missing, hey, Description is missing, hey',
      permissions: ['all_users_global'],
      participants: [''],
      is_completed: false,
    },
  ],
};

export default function taskReducer(state = initialState, action) {
  switch (action.type) {
  case CREATE_STREAM:
  case EDIT_STREAM:
  case DELETE_STREAM:
    return state;

  case SELECT_SINGLE_STREAM:
    return {
      ...state,
      selectedStream: state.streamlist.filter(stream => stream.id === action.id)
    };
  case CLEAR_STREAM_SELECTION:
    return { ...state, selectedStream: null };
  case CREATE_TASK:
    return { ...state, tasklist: [].concat(state.tasklist, action.payload) };
  case EDIT_TASK:
    return { ...state };
  case DELETE_TASK:
    return { ...state };
  default:
    return { ...state };
  }
}
