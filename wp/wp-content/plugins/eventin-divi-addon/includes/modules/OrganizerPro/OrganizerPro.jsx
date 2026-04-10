
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class OrganizerPro extends Component {

  static slug = 'eventin_organizer_pro';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__all_speakers}} />
    );
  }
}

export default OrganizerPro;
