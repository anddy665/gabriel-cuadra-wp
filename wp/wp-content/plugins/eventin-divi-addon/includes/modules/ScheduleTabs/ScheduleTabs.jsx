
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class ScheduleTabs extends Component {

  static slug = 'eventin_schedule_tabs';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__all_schedule}} />
    );
  }
}

export default ScheduleTabs;
