
// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
 

class AdvancedSearch extends Component {

  static slug = 'eventin_advanced_search';
  render() {
    return (
      <div dangerouslySetInnerHTML={{__html: this.props.__posts}} />
    );
  }
}

export default AdvancedSearch;