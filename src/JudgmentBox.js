const { Component } = wp.element;

import Grid from '@material-ui/core/Grid';
import PresentResp from './PresentResp';
import Codes from './Codes';
import Rows from './Rows';

class JudgmentBox extends Component {
    state = {
        rows:[],
        activeSelect:'',
        codes:[0,0,0,0,0,0,0,0,0,0],
        excerpts:['','','','','','','','','','']
    }

    divStyle = {
        marginTop: '50px'
    };

    handleButton = (code) => {
        if(code) {
            const codeKey = code[0]
            const selection = this.state.activeSelect ? this.state.activeSelect : 'No selection'
            this.setState((prevState) => ({
                rows: prevState.rows.filter((row) => row.code != code).concat({
                    'text':selection,
                    'code':code
                }),
                activeSelect:''
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

    handleDelete = (e) => {
        e.preventDefault()
        const code = e.target.id
        this.setState((prevState) => ({
            rows: prevState.rows.filter((row) => row.code[0] != code),
            excerpts: prevState.excerpts.filter((excerpt,i) => i != code),
            codes: prevState.codes.filter((num,i) => i != code)
        }))
    }

    handleNext = (e) => {
        e.preventDefault();
        this.setState(() => ({
            rows:[],
            activeSelect:'',
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
                <Rows 
                    rows={this.state.rows}
                    handleDelete={this.handleDelete}
                    showDelete={true}
                />
                </div>
                <button onClick={this.handleNext}>Next</button>
            </div>
        );
    }
}

export default JudgmentBox