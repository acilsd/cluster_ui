/* @flow */
// NOTE: thats just a raw placeholder
import * as React from 'react';
import styled, { css } from 'react-emotion';
import { vars } from 'helpers/vars';

import Wrapper from 'layout/Wrapper';

const EmptyContainer = styled('div')`
  display: flex;
  flex-flow: column wrap;
  justify-content: center;
  align-items: center;
  flex-grow: 1;
  background: ${vars.white};
`;

type Props = {
  match: any,
  location: any,
  history: any
};

const ErrorPage = (props: Props): React$Element<*> => {
  return (
    <Wrapper.Inner>
      <Wrapper.WhiteSection row white>
        <div css={`
          display: flex;
          flex-flow: column wrap;
          width: 100%;
          flex-grow: 1;
          justify-content: center;
          align-items: center;
        `}>
          <p css={`
            margin-bottom: 20px;
        `}>
            woopsi, this page is whiteblank for now, takie dela
          </p>

          <p css={`color: ${vars.blue}; margin-bottom: 20px;`}>
            {props.location.pathname}
          </p>
          <button onClick={(): void => props.history.goBack()} css={`
            padding: 10px;
            border: none;
            outline: none;
            background: ${vars.blue_op};
            width: 200px;
            &:hover {
              opacity: 0.7;
            };
          `}>
            GoBack()
          </button>
        </div>
      </Wrapper.WhiteSection>
    </Wrapper.Inner>
  );
};

export default ErrorPage;
