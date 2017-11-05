/* @flow */
import * as React from 'react';
import styled from 'react-emotion';

import Wrapper from 'layout/Wrapper';
import { Title } from 'layout/Typo';
import { MainButton } from 'components/Buttons';
import { TextInput } from 'components/Inputs';

const LoginContainer = styled('div')`
  display: flex;
  flex-flow: column wrap;
  justify-content: center;
  align-items: center;
  width: 100%;
  height: 100vh;
`;

const LoginWrap = styled('div')`
  display: flex;
  flex-flow: column wrap;
  justify-content: center;
  align-items: center;
  min-width: 480px;
  max-width: 640px;
  height: 320px;
  padding: 20px;
`;

import type { Store } from './redux/types';
type OwnState = {
  +user_from_input: string,
  +password_from_input: string,
};

class Auth extends React.PureComponent<Store, OwnState> {
  render(): React$Element<*> {
    const { password_from_input, user_from_input } = this.state;
    const { username, isLoggedIn, token, error } = this.props;
    return (
      <LoginContainer>
        <LoginWrap>
          <Title>
            Auth placeholder
          </Title>

          <Wrapper.Input>

          </Wrapper.Input>

          <Wrapper.Input>

          </Wrapper.Input>

          <Wrapper.Input>
            <MainButton
              text='Connect'
              disabled={false}
            />
          </Wrapper.Input>

        </LoginWrap>
      </LoginContainer>
    );
  }
}

import { connect } from 'react-redux';
import * as actions from './redux/actions';
import type { RootState } from 'helpers/types';

const mapStateToProps = (state: RootState): Store => {
  return {
    username: state.user.username,
    token:  state.user.token,
    isLoggedIn: state.user.isLoggedIn,
    error: state.user.error,
  };
};

export default connect(mapStateToProps, actions)(Auth);
