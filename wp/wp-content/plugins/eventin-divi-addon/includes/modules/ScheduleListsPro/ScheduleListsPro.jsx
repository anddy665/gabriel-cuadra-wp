
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class ScheduleListsPro extends Component {

  static slug = 'eventin_schedule_lists_pro';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__single_schedule}} />
    );
  }
}

export default ScheduleListsPro;
