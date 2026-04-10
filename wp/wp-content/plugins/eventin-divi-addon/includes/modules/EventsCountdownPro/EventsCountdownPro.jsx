
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class EventsCountdownPro extends Component {

  static slug = 'eventin_events_countdown_pro';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__single_event}} />
    );
  }
}

export default EventsCountdownPro;
