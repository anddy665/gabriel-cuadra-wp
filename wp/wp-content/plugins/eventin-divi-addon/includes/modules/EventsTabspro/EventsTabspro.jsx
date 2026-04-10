
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class EventsTabspro extends Component {

  static slug = 'eventin_events_tabs_pro';

  render() {
     return (
      <div dangerouslySetInnerHTML={{__html: this.props.__all_events}} />
    );
  }
}

export default EventsTabspro;
