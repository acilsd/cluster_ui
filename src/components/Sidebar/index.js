import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import styled from 'react-emotion';

import { vars } from 'helpers/vars';

import Wrapper from 'layout/Wrapper';

import { SideLink } from '../Links';

const InnerWrap = styled('div')`

`;

const NavBlock = styled('div')`
  display: flex;
  flex-flow: column wrap;
  background: ${vars.blue_op};
  padding-left: 40px;
`;

class Sidebar extends PureComponent {

  state = {
    taskNav: false,
  }

  static contextTypes = {
    router: PropTypes.object.isRequired
  }

  componentWillReceiveProps(props) {

  }

  render() {
    const { streams } = this.props;
    return (
      <Wrapper.Sidebar>
        <InnerWrap>
          <SideLink to='/home' text='Index' />
          <SideLink to='/trixie' text='Trixie' />
          <SideLink to='/wiki' text='Wiki' />
          {/* <NavBlock>
            <SideLink
              to='/trixie/1'
              text='Tasks Test'
            />
            <SideLink to='/trixie/2' text='Tasks Test1' />
            <SideLink to='/trixie/3' text='Tasks Test2' />
          </NavBlock> */}
          <SideLink to='/profile' text='Profile' />
          <SideLink to='/about' text='About' />
        </InnerWrap>
      </Wrapper.Sidebar>
    );
  }
}

import { connect } from 'react-redux';
import { withRouter } from 'react-router';

const mapStateToProps = (state) => {
  return {
    streams: state.tasks.streamlist
  };
};

const ConnectedSidebar = withRouter(connect(mapStateToProps)(Sidebar));

export default ConnectedSidebar;
