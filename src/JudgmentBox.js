const { Component } = wp.element;

import Grid from '@material-ui/core/Grid';
import PresentResp from './PresentResp';
import Codes from './Codes';
import Rows from './Rows';

class JudgmentBox extends Component {
    state = {
        rows:[],
        activeSelect:'',
        codes:[0,0,0,0,0,0,0,0,0],
        excerpts:['','','','','','','','','']
    }

    divStyle = {
        marginTop: '50px'
    };

    handleButton = (code) => {
        if(code) {
            const codeKey = code[0]-1
            const selection = this.state.activeSelect
            this.setState((prevState) => ({
                rows: prevState.rows.filter((row) => row.code != code).concat({
                    'text':selection,
                    'code':code
                }),
                activeCode:''
            }))
            this.state.codes[codeKey] = 1;
            this.state.excerpts[codeKey] = selection;
            if(document.getSelection) {
                if(document.getSelection().removeAllRanges) {
                    document.getSelection().removeAllRanges()
                }
            } else if(document.selection) {
                if(document.selection.empty) {
                    document.selection.empty()
                }
            } else {
                console.log("no empty function")
            }
        }
    }
    
    handleSelection = (selection) => {
        this.state.activeSelect = selection
    }

    handleNext = (e) => {
        e.preventDefault();
        this.setState(() => ({
            rows:[],
            activeSelect:'',
            clicked:0,
            codes:[0,0,0,0,0,0,0,0,0],
            excerpts:['','','','','','','','','']
        }))
        this.props.handleNext(this.state.excerpts,this.state.codes);
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
                <Rows rows={this.state.rows} />
                </div>
                <button onClick={this.handleNext}>Next</button>
            </div>
        );
    }
}

export default JudgmentBox