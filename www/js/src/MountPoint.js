/* @flow */
import * as React from 'react';
import { BrowserRouter, Switch, Route, Redirect } from 'react-router-dom';
import type { Location } from 'react-router-dom';
import Wrapper from 'layout/Wrapper';
import Sidebar from './components/Sidebar';
import TopLine from './components/TopLine';

import UserBlock from './applications/AuthApp/user';
import Auth from './applications/AuthApp';
import Trixie from './applications/TrixieApp';
import Wiki from './applications/WikiApp';
import ErrorPage from 'components/ErrorPage';

type TArgs = {
  component: React$ComponentType<*>,
  isLoggedIn: boolean,
  rest?: any,
  location?: Location
};

const PrivateRoute = ({ component: Component, isLoggedIn, ...rest }: TArgs): React.Element<*> => {
  return (
    <Route {...rest} render={(props: any): React.Element<*> => {
      return (
        isLoggedIn
          ? <Component from={props.location} {...props}/>
          : <Redirect to={{pathname: '/', state: { from: props.location }}}/>
      );
    }}/>
  );
};

const Main = (props: { isLoggedIn: boolean }): React.Element<*> => {
  return (
    <BrowserRouter>
      <Wrapper>
        <Sidebar />
        <Wrapper.Content>
          <TopLine />
          <Switch>
            <Route exact path='/' component={Auth}/>
            <PrivateRoute path='/home' isLoggedIn={props.isLoggedIn} component={UserBlock}/>
            <PrivateRoute path='/trixie' isLoggedIn={props.isLoggedIn} component={Trixie}/>
            <PrivateRoute path='/wiki' isLoggedIn={props.isLoggedIn} component={Wiki}/>
            <Route component={ErrorPage}/>
          </Switch>
        </Wrapper.Content>
      </Wrapper>
    </BrowserRouter>
  );
};

import { connect } from 'react-redux';
import type { RootState } from 'helpers/types';

const mapStateToProps = (state: RootState): { isLoggedIn: boolean } => {
  return {
    isLoggedIn: state.user.isLoggedIn,
  };
};

export default connect(mapStateToProps, null)(Main);
