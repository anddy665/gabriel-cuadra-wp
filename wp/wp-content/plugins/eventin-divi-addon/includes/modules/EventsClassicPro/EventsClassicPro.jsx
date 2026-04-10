
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class EventsClassicPro extends Component {

  static slug = 'eventin_events_classic_pro';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__all_events}} />
    );
  }
}

export default EventsClassicPro;
