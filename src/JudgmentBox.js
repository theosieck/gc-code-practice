const { Component } = wp.element;

import ReactHtmlParser from 'react-html-parser';
import Grid from '@material-ui/core/Grid';
import Button from '@material-ui/core/Button';
import PresentResp from './PresentResp';
import Codes from './Codes';
import Rows from './Rows'

class JudgmentBox extends Component {
    state = {
        rows:[],
        activeCode:'',
        clicked:0
    }

    divStyle = {
        marginTop: '50px'
    };

    handleButton = (code) => {
        this.state.activeCode = code;
        this.setState(() => ({clicked:code.substring(0,1)}))
    }
    
    handleSelection = (e) => {
        console.log(e.selection)
        console.log(this.state.activeCode)
        this.setState((prevState) => ({
            rows: prevState.rows.concat({
                'text':e.selection,
                'code':this.state.activeCode
            }),
            clicked:0
    }))
    }

    render() {
        return (
            <div>
                <div style={this.divStyle}>
                <Grid container direction="row" justify="space-between" alignItems="flex-start">
                    <Grid item xs={8} zeroMinWidth>
                    <PresentResp
                        respId={ this.props.respId }
                        response={ this.props.response }
                        handleSelection={this.handleSelection}
                    />
                    </Grid>
                    <Grid item xs={4}>
                    <Codes
                        codes={this.props.codes}
                        handleButton={this.handleButton}
                        state={this.state}
                    />
                    </Grid>
                </Grid>
                </div>
                <div style={this.divStyle}>
                <Rows
                    rows={this.state.rows}
                />
                </div>
            </div>
        );
    }
}

export default JudgmentBox