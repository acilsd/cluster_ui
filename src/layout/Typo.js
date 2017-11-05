import React from 'react';
import styled from 'react-emotion';

import { vars } from 'helpers/vars';

export const Title = styled('h1')`
  font-size: ${props => props.fz || vars.fzt};
  font-weight: ${props => props.fw || vars.fwb};
  margin: ${props => props.margin || '0 0 20px 0'};
  padding: ${props => props.padding || '0'};
  text-transform: ${props => props.transform || 'uppercase'};
  color: ${props => props.color || vars.white};
`;

export const Text = styled('p')`
  font-size: ${props => props.fz || vars.fzm};
  font-weight: ${props => props.fw || vars.fw};
  margin: ${props => props.margin || '0'};
  padding: ${props => props.padding || '0'};
  text-transform: ${props => props.transform || 'none'};
  color: ${props => props.color || vars.black};
`;
