import React, { Component } from 'react';
import styled from 'react-emotion';

import { vars } from 'helpers/vars';

const ItemBlock = styled('div')`
  display: flex;
  flex-wrap: nowrap;
  width: 200px;
  height: 190px;
  margin-bottom: 20px;
  overflow: hidden;
  border: 1px solid ${vars.purple_light};
`;

const ItemContentWrap = styled('div')`
  width: 190px;
`;

const ItemSelector = styled('div')`
  width: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
  background: ${vars.purple_light};
  color: ${vars.purple};
  font-size: 1.5em;
  overflow: hidden;
  &:hover {
    cursor: pointer;
    opacity: 0.9;
  }
`;

const ItemHeadline = styled('div')`
  text-align: center;
  margin-bottom: 20px;
  background: ${vars.blue_op};
  color: ${vars.black};
  font-size: 18px;
`;

const ItemContent = styled('div')`

`;



const TaskItem = ({
  id,
  name,
  created_by,
  created_date,
  deadline_date,
  status,
  shortcut,
  description,
  permissions,
  is_completed,
}) => {
  return (
    <ItemBlock>
      <ItemContentWrap>
        <ItemHeadline>{name}</ItemHeadline>
        <ItemContent>
          {created_by}
          {shortcut}
        </ItemContent>
      </ItemContentWrap>
      <ItemSelector>
          >
          >
          >
      </ItemSelector>
    </ItemBlock>
  );
};

export default TaskItem;
