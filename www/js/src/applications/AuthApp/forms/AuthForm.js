/* @flow */
import * as React from 'react';
import { css } from 'react-emotion';
import { vars } from 'helpers/vars';

import { Field, reduxForm } from 'redux-form';
import type { FormProps } from 'redux-form';
import { FieldInput } from 'components/Inputs';
import { MainButton } from 'components/Buttons';

type Props = FormProps & {
  handleSubmit(values: { username: string | number, password: string | number }): void
};

let AuthForm = (props: Props): React.Element<*> => {
  const { handleSubmit } = props;
  return (
    <form css={`width: 100%; display: flex; flex-flow: column wrap;`} onSubmit={handleSubmit}>
      <Field name='username' component={FieldInput} type='text' bg='test'/>
      <Field name='password' component={FieldInput} type='text' />
      <MainButton
        text='Connect'
        type='submit'
        disabled={false}
      />
    </form>
  );
};

AuthForm = reduxForm({
  form: 'auth_form'
})(AuthForm);

export default AuthForm;
