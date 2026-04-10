
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class RelatedEventPro extends Component {

  static slug = 'eventin_related_events_pro';

  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__single_event}} />
    );
  }
}

export default RelatedEventPro; 
