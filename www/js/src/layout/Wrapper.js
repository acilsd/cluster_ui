import React from 'react';

import styled from 'react-emotion';
import { vars } from 'helpers/vars';

const Wrapper = styled('div')`
  width: 100%;
  min-height: 100vh;
  display: flex;
  flex-flow: row nowrap;
  justify-content: space-between;
  background: url('/images/bg.jpg') no-repeat center;
  background-size: cover;
  min-width: 960px;
`;

Wrapper.Content = styled('div')`
  min-height: 100vh;
  display: flex;
  flex-flow: column nowrap;
  flex-grow: 1;
  padding: 40px;
  padding-top: 0;
`;

Wrapper.Inner = styled('div')`
  display: flex;
  flex-flow: column nowrap;
  flex-grow: 1;
`;

Wrapper.Grad = styled('div')`
  width: 100%;
  min-height: 100%;
  display: flex;
  flex-flow: column wrap;
  background: linear-gradient(to right, ${vars.blue}, ${vars.blue_light})
`;

Wrapper.WhiteSection = styled('div')`
  display: flex;
  width: 100%;
  min-height: calc(100vh - 80px);
  flex-flow: ${props => props.row ? 'row' : 'column wrap'};
  background: ${props => props.white ? vars.white : 'transparent'};
`;

Wrapper.Input = styled('div')`
  width: ${props => props.width || '100%'};
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 10px 0;
`;

Wrapper.Sidebar = styled('div')`
  display: flex;
  flex-flow: column nowrap;
  width: ${props => props.width || '240px'};
  padding: 20px 0;
  background: ${vars.blue_op};
`;

Wrapper.TopLine = styled('div')`
  position: relative;
  width: 100%;
  height: 40px;
  display: flex;
  justify-content: space-between;
  align-items: stretch;
  background: ${vars.blue_op};
  color: ${vars.blue_light};
  font-size: ${vars.fz} ;
  font-weight: ${vars.fwm};
`;

export default Wrapper;
