/* @flow */
import * as React from 'react';

import { vars } from 'helpers/vars';

import Wrapper from 'layout/Wrapper';
import TopLine from 'components/TopLine';

type Props = {
  user: { name: string },
  match: { url: string }
};

class UserBlock extends React.Component<Props> {
  render(): React$Element<*> {
    const { user, match } = this.props;
    return (
      <Wrapper.Content>
        <TopLine />

        <Wrapper.WhiteSection white>

          <div style={{minHeight: '100%', display: 'flex', flexFlow: 'column' ,'alignItems': 'center', 'justifyContent': 'center', flexGrow: 1}}>

            <p style={{'margin': '20px 0'}}>Welcome, <b>{user.name}</b>!</p>
            <p style={{'margin': '20px 0'}}>Nothing happened just yet, dashboard is empty</p>
            <p style={{'margin': '20px 0', color: `${vars.blue}`}}>You are here: {match.url}</p>

          </div>
        </Wrapper.WhiteSection>
      </Wrapper.Content>
    );
  }
}

export default UserBlock;
