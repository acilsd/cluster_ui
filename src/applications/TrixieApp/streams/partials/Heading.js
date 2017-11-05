/* @flow */
import * as React from 'react';
import styled from 'react-emotion';
import { vars } from 'helpers/vars';

import { Text } from 'layout/Typo';
import { Icon } from 'components/Icons';

const MainWrap = styled('div')`
  width: 100%;
  background-color: ${vars.blue_op};
  color: ${vars.red};
  display: flex;
  align-items: center;
  justify-content: space-between;
`;

const SectionBlock = styled('div')`
  display: flex;
  flex-flow: row wrap;
  padding: 10px 20px;
  padding-right: 0;
  width: 50%;
`;

type Props = {
  name?: string,
  status?: string,
};

const Heading = (props: Props): React$Element<*> => {
  return (
    <MainWrap>
      <SectionBlock>
        <Icon icon_name={'fa-fire'} />
        <Text margin='0 0 0 10px' color={vars.black}>
          {props.name}-Stream
        </Text>
        <Text margin='0 0 0 10px' color={vars.red}>
          {props.status}
        </Text>
      </SectionBlock>
    </MainWrap>
  );
};

export default Heading;
