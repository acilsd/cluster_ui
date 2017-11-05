import React, { Component } from 'react';
import Wrapper from 'layout/Wrapper';
import TopLine from 'components/TopLine';

class Wiki extends Component {
  render() {

    return (
      <Wrapper.Content>
        <TopLine />
        <Wrapper.WhiteSection white row>

        </Wrapper.WhiteSection>
      </Wrapper.Content>
    );
  }
}

export default Wiki;
