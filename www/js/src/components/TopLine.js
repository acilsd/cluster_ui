/* @flow */
import * as React from 'react';
import styled, { css } from 'react-emotion';
import type { RouterHistory, Location } from 'react-router-dom';

import { vars } from 'helpers/vars';

import Wrapper from 'layout/Wrapper';

import { TopLineButton } from './Buttons';

const TopInfoSection = (props: { text: string, label: string, className?: string, full?: boolean }): React.Element<*> => {
  return (
    <div
      className={props.className}
      css={`
        background: ${vars.blue_op};
        padding: 0 5px;
        margin: 0;
        min-width: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-grow: ${props.full ? 1 : 0};
        & > b {
          display: inline-block;
          margin-right: 10px;
          color: ${vars.black};
        }
        `}
    >
      <b>{props.label}</b>
      <p>{props.text}</p>
    </div>
  );
};

type TProps = {
  location: Location,
  history: RouterHistory,
  username: string,
  logout(): void,
};

class TopLine extends React.PureComponent<TProps> {
  handleLogOut = async (): Promise<*> => {
    await localStorage.removeItem('TrixieAuthTokenDangerouslyExposed');
    await localStorage.removeItem('TrixieAuthUsernameDangerouslyExposed');
    await this.props.logout();
    this.props.history.push('/');
  }

  render(): any {
    const { location, username } = this.props;
    const isIndex = location.pathname === '/';

    return (
      !isIndex &&
      <Wrapper.TopLine>

        <TopLineButton
          text={username}
        />

        <TopInfoSection
          label={'Trixie'}
          text={'0.0.2.p.a'}
          full
        />

        <TopInfoSection
          label={'location: '}
          text={location.pathname}
          full
        />

        <TopInfoSection
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
import { connect } from 'react-redux';
import * as actions from '../applications/AuthApp/redux/actions';
import type { RootState } from 'helpers/types';
import type { Store, ActionType } from '../applications/AuthApp/redux/types';

const mapStateToProps = (state: RootState): { username: string } => {
  return {
    username: state.user.username,
  };
};

const mapDispatchToProps = {
  logout: actions.logout
};

const ConnectedTopLine = withRouter(connect(mapStateToProps, actions)(TopLine));

export default ConnectedTopLine;
