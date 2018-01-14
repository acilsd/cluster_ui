/* @flow */
import * as React from 'react';
import { css } from 'react-emotion';
import { vars } from 'helpers/vars';
import type { FieldProps } from 'redux-form';

type InputProps = {
  className?: string,
  name?: string,
  type?: string,
  value: string,
  disabled?: boolean,
  placeholder?: string,
  handleChange?: void,
  bg?: string,
};

type RFProps = FieldProps & {
  bg?: string
};

export const TextInput = (props: InputProps): React.Element<*> => (
  <input
    className={props.className || ''}
    name={props.name}
    type={props.type || 'text'}
    value={props.value}
    disabled={props.disabled}
    placeholder={props.placeholder}
    onChange={props.handleChange}
    css={`
      width: 100%;
      margin-bottom: 20px;
      text-align: center;
      padding: 5px 10px;
      color: ${vars.purple};
      font-family: ${vars.font};
      font-size: ${vars.fz};
      font-weight: ${vars.fw};
      outline: none;
      background: ${props.bg || vars.white};
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
    `}
  />
);

export const FieldInput = (field: RFProps): React.Element<*> => {
  return (
    <input
      {...field.input}
      css={`
        width: 100%;
        margin-bottom: 20px;
        text-align: center;
        padding: 5px 10px;
        color: ${vars.purple};
        font-family: ${vars.font};
        font-size: ${vars.fz};
        font-weight: ${vars.fw};
        outline: none;
        background: ${field.bg || vars.white};
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
      `}
    />
  );
};
