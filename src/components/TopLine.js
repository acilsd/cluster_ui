import React, { Component } from 'react';
import styled from 'react-emotion';

import { vars } from 'helpers/vars';

import Wrapper from 'layout/Wrapper';
  
import { TopLineButton } from './Buttons';

const TopInfoSection = ({ text, label, className }) => {
  return (
    <div className={className}>
      <b>{label}</b>
      <p>{text}</p>
    </div>
  );
};

const StyledTopInfoSection = styled(TopInfoSection)`
  background: ${vars.blue_op};
  padding: 0 5px;
  margin: 0;
  min-width: 150px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-grow: ${props => props.full ? 1 : 0};
  & > b {
    display: inline-block;
    margin-right: 10px;
    color: ${vars.black};
  }
`;

const DropDownItem = styled('div')`
  padding: 10px;
  border-bottom: 1px solid ${vars.blue_op};
  &:hover {
    cursor: pointer;
    opacity: 0.7;
  }
  &:last-child {
    border-bottom: none;
  }
`;

const DropDown = ({ className }) => {
  return (
    <div className={className}>
      <DropDownItem>This</DropDownItem>
      <DropDownItem>Is</DropDownItem>
      <DropDownItem>Just</DropDownItem>
      <DropDownItem>A</DropDownItem>
      <DropDownItem>Placeholder</DropDownItem>
    </div>
  );
};

const StyledDropDown = styled(DropDown)`
  position: absolute;
  top: 40px;
  background: ${vars.blue_op};
  color: ${vars.black};
  left: 0;
  min-width: 150px;
  display: flex;
  flex-flow: column wrap;
  height: ${props => props.show ? 'auto' : 0};
  visibility: ${props => props.show ? 'visible' : 'hidden'};;
  overflow: hidden;
  white-space: nowrap;
  transition: all 0.3s ease-in;
`;

class TopLine extends Component {
  state = {
    dropMenu: false
  }

  toggleUserDrop = () => {
    this.setState({ dropMenu: !this.state.dropMenu });
  }

  handleLogOut = () => {
    this.props.history.push('/');
  }

  render() {
    const user = 'test';
    const { location } = this.props;
    const { dropMenu } = this.state;
    return (
      <Wrapper.TopLine>

        <TopLineButton
          text={user}
        />

        <StyledTopInfoSection
          label={'Trixie'}
          text={'0.0.2.p.a'}
          full
        />

        <StyledTopInfoSection
          label={'location: '}
          text={location.pathname}
          full
        />

        <StyledTopInfoSection
          label={'clustername:'}
          text={'localhost'}
          full
        />

        <TopLineButton
          text={'LOGOUT'}
          iconClass={'sign-out'}
          handleClick={this.handleLogOut}
        />

      </Wrapper.TopLine>
    );
  }
}

import { withRouter } from 'react-router';
const ConnectedTopLine = withRouter(TopLine);

export default ConnectedTopLine;
