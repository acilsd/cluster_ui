/* @flow */

import React, { PureComponent } from 'react';
import styled from 'react-emotion';
import { vars } from 'helpers/vars';

const BaseInput = ({
  className,
  value = '',
  type = 'text',
  disabled,
  placeholder = 'placeholder',
  handleChange = null,
  name = '',
}) => {
  return (
    <input
      className={className}
      name={name}
      value={value}
      type={type}
      disabled={disabled}
      placeholder={placeholder}
      onChange={handleChange}
    />
  );
};

export const TextInput = styled(BaseInput)`
  width: 100%;
  text-align: center;
  padding: 5px 10px;
  color: ${vars.purple};
  font-family: ${vars.font};
  font-size: ${vars.fz};
  font-weight: ${vars.fw};
  outline: none;
  background: ${props => props.bg || vars.white};
  border: 1px solid ${vars.purple_light};
  &::placeholder {
    color: ${vars.red};
    text-align: left;
  }
  &:disabled {
    opacity: 0.3;
    cursor: not-allowed;
  }
  &:focus {
    border: 1px solid ${vars.purple};
  }
  &:focus::placeholder {
    text-align: center;
    opacity: 0.3;
  };
`;
