const { Component } = wp.element;

import Grid from '@material-ui/core/Grid';
import PresentExp from './PresentExp';
import Codes from './Codes';
import Rows from './Rows';
import ShowFeedback from './ShowFeedback';

class PracticeBox extends Component {
	genState = () => {
		const results = this.props.resultsObj
		let codes = [];
		let excerpts = [];
		let rows = [];
		for(let i=1;i<10;i++) {
			const codeNum = parseInt(results[`code${i}`])
			codes[i] = codeNum
			excerpts[i] = results[`excerpt${i}`]
			if(codeNum===1) {
				rows[i] = {
					text: excerpts[i],
					code: `${i}. ${this.props.codes[i]}`
				}
			}
		}
		return {
			rows,
			codes,
			excerpts,
			feedback: false,
			correctCodes:[],
			missedCodes:[],
			falsePositives:[]
		}
	}

    state = this.props.resultsObj ? this.genState() : {
        rows:[],
        activeSelect:'',
        codes:[0,0,0,0,0,0,0,0,0,0],
        excerpts:['','','','','','','','','',''],
				feedback: false,
				correctCodes:[],
				missedCodes:[],
				falsePositives:[]
    }

    divStyle = {
        marginTop: '50px'
    };

    handleCodeButton = (code) => {
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
				console.log("deleting...")
        const code = e.target.id
				console.log(code)
        this.setState((prevState) => ({
            rows: prevState.rows.filter((row) => row.code[0] != code),
            excerpts: prevState.excerpts.map((excerpt,i) => i == code ? '' : excerpt),
            codes: prevState.codes.map((num,i) => i == code ? 0 : num)
        }))
    }

		handleSubmit = (e) => {
			e.preventDefault();
			const correctCodes = [];
			const missedCodes = [];
			const falsePositives = [];
			this.state.codes.forEach((code,index) => {
				if(index>0 && index<this.props.codes.length) {
					if(code) {
						// learner selected
						if(this.props.goldCodes.includes(index.toString())) {
							correctCodes.push(index);
						} else {
							falsePositives.push(index)
						}
					} else {
						// learner did not select
						if(this.props.goldCodes.includes(index.toString())) {
							missedCodes.push(index)
						}
					}
				}
			});
			this.setState(() => ({feedback:true,correctCodes,missedCodes,falsePositives}));
		}

    handleNext = (e) => {
        e.preventDefault();
        this.setState(() => ({
            rows:[],
            activeSelect:'',
            codes:[0,0,0,0,0,0,0,0,0],
            excerpts:['','','','','','','','',''],
						feedback: false,
						correctCodes:[],
						missedCodes:[],
						falsePositives:[]
        }))
        this.props.handleNext(this.state.excerpts,this.state.codes,this.state.correctCodes,this.state.missedCodes,this.state.falsePositives);
    }

    render() {
        return (
            <div>
                <div style={this.divStyle}>
                <Grid container direction="row" justify="space-between" alignItems="flex-start">
                    <Grid item xs={8} zeroMinWidth>
                    <PresentExp
                        expId={ this.props.expId }
                        response={ this.props.response }
                        handleSelection={this.handleSelection}
                    />
                    </Grid>
                    <Grid item xs={4}>
                    <Codes
                        codes={this.props.codes}
                        handleButton={this.handleCodeButton}
                        state={this.state}
                    />
                    </Grid>
                </Grid>
                </div>
                <div style={{marginTop:"25px"}}>
                <Rows
                    rows={this.state.rows}
                    handleDelete={this.handleDelete}
                    showDelete={true}
                />
                </div>
                {!this.state.feedback && <button style={{marginTop:"10px"}} onClick={this.handleSubmit}>Submit</button>}
								{this.state.feedback &&
									<ShowFeedback
										correctCodes={this.state.correctCodes}
										missedCodes={this.state.missedCodes}
										falsePositives={this.state.falsePositives}
										codeLabels={this.props.codes}
										handleNext={this.handleNext}
									/>
								}
            </div>
        );
    }
}

export default PracticeBox
