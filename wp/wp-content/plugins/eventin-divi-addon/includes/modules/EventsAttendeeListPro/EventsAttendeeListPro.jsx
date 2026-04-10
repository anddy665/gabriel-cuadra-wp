
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class EventsAttendeeListPro extends Component {

  static slug = 'eventin_events_attendee_list';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__single_event}} />
    );
  }
}

export default EventsAttendeeListPro;
