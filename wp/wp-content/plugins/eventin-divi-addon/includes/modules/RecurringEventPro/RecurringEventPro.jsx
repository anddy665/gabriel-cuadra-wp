
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class RecurringEventPro extends Component {

  static slug = 'eventin_recurring_events';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__single_event}} />
    );
  }
}

export default RecurringEventPro;
