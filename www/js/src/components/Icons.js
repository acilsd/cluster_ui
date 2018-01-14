/* @flow */
// NOTE: иконочные шрифты - вебдванольное говно, но щитоподелать, рсиовать эсвеге некому
import * as React from 'react';
import styled from 'react-emotion';
import { vars } from 'helpers/vars';

import { Text } from 'layout/Typo';

type Props = {
  icon_name: string,
  className: string,
  clickHandler?: void,
};

const IconConstructor = (props: Props): React$Element<*> => {
  return (
    <div onClick={props.clickHandler} className={props.className}>
      <i class={`fa ${props.icon_name}`} aria-hidden='true'/>
    </div>
  );
};

export const Icon = styled(IconConstructor)`
  display: block;
  &:hover {
    cursor: ${props => props.clickHandler ? 'pointer' : 'default'};
  };
`;
