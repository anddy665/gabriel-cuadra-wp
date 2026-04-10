
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class EventsOnelinePro extends Component {

  static slug = 'eventin_events_oneline_pro';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__all_events}} />
    );
  }
}

export default EventsOnelinePro;
