
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class SpeakerSlider extends Component {

  static slug = 'eventin_speaker_slider';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__all_speakers}} />
    );
  }
}

export default SpeakerSlider;
