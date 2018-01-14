/* @flow */
import * as React from 'react';
import styled from 'react-emotion';
import type { RouterHistory, Location } from 'react-router-dom';

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

import { Title } from 'layout/Typo';
import AuthForm from './forms/AuthForm';

import type { Store, ActionType } from './redux/types';

type TAuth = Store & {
  auth(data: ActionType): any,
  restore_session(data: { token: string, username: string }): any,
  history: RouterHistory,
  location: Location,
};

import { BrowserRouter, Switch, Route, Redirect } from 'react-router-dom';

class Auth extends React.Component<TAuth> {

  componentDidMount =  async (): any => {
    const StoredToken = localStorage.getItem('TrixieAuthTokenDangerouslyExposed');
    const StoredUsername = localStorage.getItem('TrixieAuthUsernameDangerouslyExposed');
    if (StoredToken && StoredUsername) {
      this.props.restore_session({ token: StoredToken, username: StoredUsername });
    }
  }

  submit = async (values: { username: string, password: string }): Promise<*> => {
    await this.props.auth({
      username: values.username,
      password: values.password,
    });

    if (this.props.token.length) {
      await localStorage.setItem('TrixieAuthTokenDangerouslyExposed', this.props.token);
      await localStorage.setItem('TrixieAuthUsernameDangerouslyExposed', this.props.username);
      this.props.history.push('/home');
    }
  }

  render(): React.Element<*> {
    const { isLoggedIn, location } = this.props;
    const origin = location.state ? location.state.from.pathname : '/home';

    return (
      isLoggedIn
        ? <Redirect to={origin}/>
        : <LoginContainer>
          <LoginWrap>
            <Title>
              Auth placeholder
            </Title>
            <AuthForm onSubmit={this.submit}/>
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
  };
};

const mapDispatchToProps = {
  auth: actions.auth,
  restore_session: actions.restore_session,
};

export default connect(mapStateToProps, mapDispatchToProps)(Auth);
