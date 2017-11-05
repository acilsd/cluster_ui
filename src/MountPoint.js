/* @flow */
import * as React from 'react';
import { BrowserRouter, Switch, Route, Redirect } from 'react-router-dom';

import Wrapper from 'layout/Wrapper';
import Sidebar from './components/Sidebar';
import UserBlock from './applications/AuthApp';
import Auth from './applications/AuthApp/Auth';
import Trixie from './applications/TrixieApp';
import Wiki from './applications/WikiApp';
import ErrorPage from 'components/ErrorPage';

const Main = (): React$Element<*> => {
  return (
    <BrowserRouter>
      <Wrapper>
        <Sidebar />
        <Switch>
          <Route exact path='/' component={Auth}/>
          <Route path='/home' component={UserBlock}/>
          <Route path='/trixie' component={Trixie}/>
          <Route path='/wiki' component={Wiki}/>
          <Route component={ErrorPage}/>
        </Switch>
      </Wrapper>
    </BrowserRouter>
  );
};

export default Main;
