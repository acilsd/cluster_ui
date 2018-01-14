#!/bin/bash

docker run -it --rm -p 8050:514 truths:rsyslog bash
#docker run -d -p 8000:514 truths:rsyslog