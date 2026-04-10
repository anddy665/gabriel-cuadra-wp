
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class EventsTabs extends Component {

  static slug = 'eventin_events_tabs';

  render() {
     return (
      <div dangerouslySetInnerHTML={{__html: this.props.__all_events}} />
    );
  }
}

export default EventsTabs;
