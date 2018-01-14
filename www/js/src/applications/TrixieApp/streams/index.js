/* @flow */
import * as React from 'react';
import styled from 'react-emotion';
import { vars } from 'helpers/vars';

import EmptyPage from 'components/EmptyPage';
import SingleStream from './SingleStream';

const StreamContainer = styled('div')`
  display: flex;
  flex-flow: column wrap;
  flex-grow: 1;
  min-width: 400px;
  min-height: calc(400px + 80px);
`;

type Props = {
  highlightSingleStream: any,
  streams: any,
};

type State = {
  selectedStream?: any
};

class StreamList extends React.Component<Props, State> {
  state = {
    selectedStream: null
  }

  selectStream = (id: number) => {
    this.props.highlightSingleStream(id);
  }

  render(): any {
    const { streams } = this.props;
    return (
      <StreamContainer>
        {
          streams.length <= 0 &&
          <EmptyPage
            text={['Looks like we haev no streams atm', 'May be you should create one?']}
            button='Create new stream'
            action={(): void => console.log('yay')}
          />
        }
        {
          streams.map((stream: any): any => {
            return <SingleStream key={stream.id} handleSelect={this.selectStream} {...stream}/>;
          })
        }

      </StreamContainer>
    );
  }
}

import { connect } from 'react-redux';
import * as actions from '../redux/actions';

const mapStateToProps = (state: any): any => {
  return {
    streams: state.tasks.streamlist
  };
};

export default connect(mapStateToProps, actions)(StreamList);
