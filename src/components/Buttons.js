/* @flow */
import * as React from 'react';
import styled from 'react-emotion';
import { vars } from 'helpers/vars';

type Props = {
  className: string,
  handleClick: void,
  iconClass: string,
  text?: string,
  disabled?: boolean,
};

const BaseButton = (props: Props): React$Element<*> => {
  return (
    <button
      className={props.className}
      onClick={props.handleClick}
      disabled={props.disabled}
    >
      {props.text}
    </button>
  );
};

const TopButton = (props: Props): React$Element<*> => {
  return (
    <button
      className={props.className}
      onClick={props.handleClick}
      disabled={props.disabled}
    >
      <i class={`fa fa-${props.iconClass}`} aria-hidden='true'></i>
      <p>{props.text}</p>
    </button>
  );
};

export const MainButton = styled(BaseButton)`
  width: 100%;
  text-decoration: none;
  outline: none;
  background: ${vars.white};
  text-transform: none;
  font-family: inherit;
  font-size: ${vars.fz};
  border: 1px solid ${vars.purple};
  color: ${vars.purple};
  padding: 5px 15px;
  &:hover {
    cursor: pointer;
    color: ${vars.purple_light};
    border: 1px solid ${vars.purple_light};
  }
  &:disabled {
    color: ${vars.red};
    cursor: not-allowed;
    opacity: 0.5;
    &:hover {
      border: 1px solid ${vars.purple};
      color: ${vars.red};
    }
}
`;

export const TopLineButton = styled(TopButton)`
  border: none;
  outline: none;
  background: ${vars.blue_op};
  padding: 0 20px;
  margin: 0;
  min-width: 150px;
  flex-grow: ${props => props.full ? 1 : 0};
  display: flex;
  &:hover {
    cursor: pointer;
  };
  & > i {
    color: ${vars.blue_light};
    margin-right: 10px;
  };
  &:hover > i {
    color: ${vars.blue};
  };
  &:hover > p {
    opacity: 0.6;
  };
`;
