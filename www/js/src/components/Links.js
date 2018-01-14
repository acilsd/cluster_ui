import React from 'react';
import { NavLink } from 'react-router-dom';
import styled from 'react-emotion';
import { vars } from 'helpers/vars';

export const BaseLink = (props) => {
  return (
    <NavLink
      className={props.className}
      to={props.to || '/'}
    >
      {props.text || 'No {text} prop was provided'}
    </NavLink>
  );
};

export const SideLink = styled(BaseLink)`
  font-size: ${vars.fzm};
  font-weight: ${vars.fwb};
  display: flex;
  width: 100%;
  height: 65px;
  align-items: center;
  text-align: left;
  text-decoration: none;
  text-transform: uppercase;
  padding: 0 20px;
  color: ${vars.blue_light};
  transition: all 0.2s ease;
  &:hover {
    cursor: pointer;
    color: ${vars.blue};
    border-right: 5px solid ${vars.blue};
  }
  &.active {
    color: ${vars.blue};
    border-right: 5px solid ${vars.blue};
  }
  &.active:hover {
    cursor: default;
  }
`;
