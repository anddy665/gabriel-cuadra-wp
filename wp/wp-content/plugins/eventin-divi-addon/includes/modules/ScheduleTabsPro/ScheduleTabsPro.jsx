
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class ScheduleTabsPro extends Component {

  static slug = 'eventin_schedule_tabs_pro';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__all_schedule}} />
    );
  }
}

export default ScheduleTabsPro;
