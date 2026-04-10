
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class EventsCalendarpro extends Component {

  static slug = 'eventin_events_calendarpro';

  render() {
    
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__all_events}} />
    );
  }
}

export default EventsCalendarpro;
