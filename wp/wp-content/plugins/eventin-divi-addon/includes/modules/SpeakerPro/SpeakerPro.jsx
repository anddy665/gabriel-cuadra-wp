
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class SpeakerPro extends Component {

  static slug = 'eventin_speaker_pro';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__all_speakers}} />
    );
  }
}

export default SpeakerPro;
