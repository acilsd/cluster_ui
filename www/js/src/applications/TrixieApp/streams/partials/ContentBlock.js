import * as React from 'react';
import styled from 'react-emotion';
import { vars } from 'helpers/vars';
import { Text } from 'layout/Typo';

const Block = styled('div')`
  display: flex;
  flex-flow: column wrap;
  text-align: left;
  overflow: hidden;
  margin-right: 40px;
  &:last-child {
    margin-right: 0;
  }
`;

type Props = {
  label?: string,
  text?: string,
};

const ContentBlock = (props: Props): React$Element<*> => {
  return (
    <Block>
      <Text>{props.label}:</Text>
      <Text>{props.text}</Text>
    </Block>
  );
};

export default ContentBlock;
