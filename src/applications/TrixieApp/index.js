import React, { Component } from 'react';

import Wrapper from 'layout/Wrapper';
import TopLine from 'components/TopLine';

import StreamList from './streams';

import { Route } from 'react-router-dom';

import axios from 'axios';

class TaskManager extends Component {

  render() {
    const { match } = this.props;

    return (
      <Wrapper.Content>
        <TopLine />
        <Wrapper.WhiteSection row>
          <Route exact path={'/trixie'} component={StreamList}/>
        </Wrapper.WhiteSection>
      </Wrapper.Content>
    );
  }
}

export default TaskManager;
