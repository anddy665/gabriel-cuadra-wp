
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class EventsTicketFormPro extends Component {

  static slug = 'eventin_events_ticket_form_pro';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__single_event}} />
    );
  }
}

export default EventsTicketFormPro;
