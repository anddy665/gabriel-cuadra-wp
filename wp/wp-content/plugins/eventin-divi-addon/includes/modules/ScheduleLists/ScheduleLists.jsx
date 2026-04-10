
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class ScheduleLists extends Component {

  static slug = 'eventin_schedule_lists';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__single_schedule}} />
    );
  }
}

export default ScheduleLists;
