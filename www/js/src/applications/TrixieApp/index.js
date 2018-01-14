import React, { Component } from 'react';
import Wrapper from 'layout/Wrapper';
import StreamList from './streams';

import { Route } from 'react-router-dom';

import axios from 'axios';

class TaskManager extends Component {

  render() {
    const { match } = this.props;

    return (
      <Wrapper.Inner>
        <Wrapper.WhiteSection row>
          <Route exact path={'/trixie'} component={StreamList}/>
        </Wrapper.WhiteSection>
      </Wrapper.Inner>
    );
  }
}

export default TaskManager;
