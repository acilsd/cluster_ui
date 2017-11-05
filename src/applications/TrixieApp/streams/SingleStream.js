import React, { Component } from 'react';
import styled from 'react-emotion';
import { vars } from 'helpers/vars';

import { BaseLink } from 'components/Links';

import ContentBlock from './partials/ContentBlock';
import StreamHeading from './partials/Heading';

const Stream = styled('div')`
  display: flex;
  flex-flow: column wrap;
  width: 100%;
  margin-bottom: 40px;
  background-color: ${vars.white};
  &:last-child {
    margin-bottom: 0;
  }
`;

const Content = styled('div')`
  padding: 20px;
  min-height: 200px;
  display: flex;
  flex-flow: row wrap;
  justify-content: space-between;
`;

const BlockWrap = styled('div')`
  display: flex;
  flex-flow: row wrap;
  width: 50%;
`;

const ButtonLink = styled(BaseLink)`
  width: 100%;
  text-align: center;
  text-decoration: none;
  text-transform: uppercase;
  padding: 10px;
  color: ${vars.purple};
  background: ${vars.blue_op};
  &:hover {
    cursor: pointer;
    opacity: 0.8;
  }
`;

class SingleStream extends Component {
  state = {

  }

  selectInfoBlock = (type) => {
    console.log(type);
  }

  render() {
    const {
      id,
      type,
      stream_type,
      stream_name,
      permissions,
      created_by,
      created_date,
      status,
      dead_line,
      priority,
      shortcut,
      description,
    } = this.props;
    return (
      <Stream>
        <StreamHeading name={stream_name} status={status} />
        <Content>
          <BlockWrap>
            <ContentBlock label='Priority' text={priority}/>
            <ContentBlock label='created_by' text={created_by}/>
            <ContentBlock label='created_date' text={created_date}/>
            <ContentBlock label='dead_line' text={dead_line || 'until finished'}/>
            <ContentBlock label='shortcut' text={shortcut}/>
          </BlockWrap>
        </Content>

        <ButtonLink to={`/trixie/${id}`} text={'Stream Page'}/>
      </Stream>
    );
  }
}

export default SingleStream;
