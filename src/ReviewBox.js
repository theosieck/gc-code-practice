const { Component } = wp.element;

import ReactHtmlParser from 'react-html-parser';
import Matches from './Matches'
import ReviewSet from './ReviewSet'

class ReviewBox extends Component {
    state = {
        clicked:[0,0,0,0,0,0,0,0,0,0],
        matchExcerpts:[]
    }

    divStyle = {marginTop: '50px'}

    handleButton = (e) => {
        e.preventDefault();
        const codeNum = parseInt(e.target.textContent[0]);
        this.setState((prevState) => ({
            clicked: prevState.clicked.map((num,i) => i==codeNum ? (1 - num) : num)
        }))
        // this.state.clicked[codeNum] = ;
    }

    handleNext = (e) => {
        e.preventDefault();
        const excerpts = [];
        this.state.clicked.forEach((codeNum,i) => codeNum == 1 ? excerpts[i]=this.props.reviewSet[i] : excerpts[i]=this.state.matchExcerpts[i])
        const clicked = []
        excerpts.forEach((excerpt,i) => excerpt ? clicked[i] = 1 : excerpts[i] = '')
        this.state.clicked = [0,0,0,0,0,0,0,0,0,0];
        this.state.matchExcerpts = []
        this.props.handleNext(excerpts,clicked)
    }

    setMatches = (matches) => {
        matches.forEach((match,i) => this.state.matchExcerpts[i] = (match[0][1].length < match[1][1].length ? match[0][1] : match[1][1]))
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
                    setMatches={this.setMatches}
                />
            </div>
                <button onClick={this.handleNext}>Next</button>
            </div>
        );
    }
}

export default ReviewBox;