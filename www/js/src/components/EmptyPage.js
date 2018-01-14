/* @flow */
import * as React from 'react';
import styled from 'react-emotion';
import { vars } from 'helpers/vars';

const EmptyContainer = styled('div')`
  display: flex;
  flex-flow: column wrap;
  justify-content: center;
  align-items: center;
  flex-grow: 1;
  background: ${vars.white};
`;

type Props = {
  text: any,
  button?: any,
  action: any,
};

const EmptyPage = (props: Props): React$Element<*> => {
  return (
    <EmptyContainer>
      {props.text.map((t: string, idx: React$Key): React$Element<*> => {
        return <p key={idx}>{t}</p>;
      })}
      <button onClick={props.action}> click </button>
    </EmptyContainer>
  );
};

export default EmptyPage;
