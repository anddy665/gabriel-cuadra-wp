
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class EventsCalendarlist extends Component {

  static slug = 'eventin_events_calendarlist';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__all_events}} />
    );
  }
}

export default EventsCalendarlist;
