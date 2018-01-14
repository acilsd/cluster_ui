STREAM_FIELDS: {
      id: 1,
      type: 'stream_unit',
      stream_type: 'core_global',
      stream_name: 'UI',
      permissions: ['system'],
      created_by: 'root_system',
      created_date: '01.01.1010',
      dead_line: null,
      priority: 'major',
      shortcut: 'Improve UI',
      description: 'Sooper dooper description',
},

TASK_FIELDS: {
      id: 1,
      stream_name: 'UI',
      name: 'Stream/Tasks filters',
      branch_tag: 'feature_1488_fix_shit',
      created_by: 'root_system',
      created_date: '14.88.1488',
      deadline_date: '14.88.1488',
      status: 'pending',
      shortcut: 'shortcut',
      description: 'Description is missing, hey',
      permissions: ['all_users_global'],
      participants: [''],
      is_completed: false,
},

root_task.clustername.truths.world
{
  type: 'task_unit'
}
=>
{
  streamname.root_task.clustername.truths.world {
    type: 'stream_unit',
    id: 1,
    stream_type: core_global,
    permissions: ['system'],
    created_by: root_system,
    created_date: 01.01.1010,
    priority: major,
  }
  =>
  {
    taskname.streamname.root_task.clustername.truths.world {
      type: 'task_item'
      id: '1'
      name: 'name',
      in_stream: 'parent_unit_streamname',
      created_by: 'testuser',
      created_date: '14.88.1488',
      deadline_date: '14.88.1488',
      status: 'pending',
      shortcut: 'shortcut',
      description: 'descr',
      permissions: ['all_users_global', 'Vasya', 'Petya'],
      is_completed: false,
    }
    =>
    {
      comment.taskname.streamname.root_task.clustername.truths.world {
        type: 'task_comment',
        text: 'Vasya, tvoy cod - govno',
        by_user: 'Petya',
        date: '31.12.2017',
      }
    }
    =>
    {
      comment_2.taskname.streamname.root_task.clustername.truths.world {
        type: 'task_comment',
        text: 'Petya, idi na hui, ne viebivaysya',
        by_user: 'Vasya',
        date: '01.01.2018',
      }
    }
  }
}

tl;dr

stream_unit {

  task_item {

    task_comment {//}
    task_comment_2 {//}
  }
  task_item_2 {

    task_comment {//}
    task_comment_2 {//}
  }
}

'stream_unit': collections of tasks/projects, one major issue (e.g. stream 'API_featurename', includes a bunch of tasks somehow connected with this feature)
'task_item': nuff said, single task
'task_comment': nuff said x2

stream_unit is goin to have some inner_types. For now we haev only one: [core_global] = implementing some core global feature (C.O.).
permissions:
['system']: only users with root_privileges (e.g. developers, system admins etc) haev permissions to create|edit|delete task_item, invite people and shit
['all_users_global']: all users haev permissions to create|edit|delete task_item, invite people and shit
['Username'] => user Username haes permissions to create|edit|delete task_item, invite people and shit
