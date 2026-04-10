
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class EtnSpeaker extends Component {

  static slug = 'eventin_speaker';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__all_speakers}} />
    );
  }
}

export default EtnSpeaker;
