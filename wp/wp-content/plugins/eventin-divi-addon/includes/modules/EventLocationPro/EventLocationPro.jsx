
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class EventLocationPro extends Component {

  static slug = 'eventin_location_pro';

  render() { 
    return ( 
      <div dangerouslySetInnerHTML={{__html: this.props.__location}} />
    );
  }
}

export default EventLocationPro;
