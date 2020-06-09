const { Component } = wp.element;

import Grid from '@material-ui/core/Grid';
import PresentResp from './PresentResp';
import Codes from './Codes';
import Rows from './Rows'

class JudgmentBox extends Component {
    state = {
        rows:[],
        activeCode:'',
        clicked:0,
        codes:[0,0,0,0,0,0,0,0,0],
        excerpts:['','','','','','','','','']
    }

    divStyle = {
        marginTop: '50px'
    };

    handleButton = (code) => {
        this.state.activeCode = code;
        const codeKey = code[0]-1

        this.setState(() => ({
            clicked:code[0],
            rows:this.state.rows.filter((row) => row.code != code)
        }))

        this.state.codes[codeKey] = 1 - this.state.codes[codeKey]
        this.state.excerpts[codeKey] = ''
    }
    
    handleSelection = (e) => {
        const code = this.state.activeCode;
        if(code) {
            const codeKey = code[0]-1
            const selection = e.selection;
            this.setState((prevState) => ({
                rows: prevState.rows.concat({
                    'text':selection,
                    'code':code
                }),
                clicked:0,
                activeCode:''
            }))
            this.state.excerpts[codeKey] = selection;
        }
    }

    handleNext = (e) => {
        e.preventDefault();
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
                <Rows
                    rows={this.state.rows}
                />
                </div>
                <button onClick={this.handleNext}>Next</button>
            </div>
        );
    }
}

export default JudgmentBox