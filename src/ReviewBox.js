const { Component } = wp.element;

import ReactHtmlParser from 'react-html-parser';
import Matches from './Matches'
import ReviewSet from './ReviewSet'

class ReviewBox extends Component {
    state = {
        clicked:[0,0,0,0,0,0,0,0,0]
    }

    divStyle = {marginTop: '50px'}

    handleButton = (e) => {
        e.preventDefault();
        const codeNum = parseInt(e.target.textContent[0])-1;
        this.setState((prevState) => ({
            clicked: prevState.clicked.map((num,i) => i==codeNum ? (1 - num) : num)
        }))
        // this.state.clicked[codeNum] = ;
    }

    render() {
        return (
            <div>
            <div style={this.divStyle}>
                <h2>Case: {this.props.respId}</h2>
                {ReactHtmlParser(this.props.response)}
            </div>
            <div style={this.divStyle}>
                <ReviewSet 
                    codes={this.props.codes}
                    reviewSet={this.props.reviewSet}
                    handleButton={this.handleButton}
                    state={this.state}
                />
                <Matches
                    codes={this.props.codes}
                    matches={this.props.matches}
                />
            </div>
            </div>
        );
    }
}

export default ReviewBox;