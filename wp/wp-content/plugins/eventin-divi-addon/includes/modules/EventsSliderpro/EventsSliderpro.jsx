
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class EventsSliderpro extends Component {

  static slug = 'eventin_events_slider_pro';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__all_events}} />
    );
  }
}

export default EventsSliderpro;
