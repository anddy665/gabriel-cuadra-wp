
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies 
class EtnEvents extends Component {

  static slug = 'eventin_events';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__all_events}} />
    );
  }
}

export default EtnEvents;
