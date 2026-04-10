
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class EventsCalendar extends Component {

  static slug = 'eventin_events_calendar';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__all_events}} />
    );
  }
}

export default EventsCalendar;
